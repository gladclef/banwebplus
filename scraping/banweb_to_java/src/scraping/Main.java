package scraping;

import java.io.IOException;

import structure.Semester;
import structure.Subject;

public class Main
{
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff";
	
	public static void main(String[] args) throws IOException
	{
		Availabilities availabilities = new Availabilities(baseurl);
		
		availabilities.scrape();
		
		for (Semester semester : availabilities.getSemesters())
		{
			System.out.println(semester.getCalendarYear() + " " + semester.getSemesterName());
		}
		
		System.out.println("");
		
		for (Subject subject : availabilities.getSubjects())
		{
			//System.out.println(subject.getLongName());
		}
	}
}
