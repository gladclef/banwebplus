package main.java.structure;

import java.util.ArrayList;
import java.util.List;

/**
 * Contains all courses offered for a given subject + semester.
 */
public class SemesterAndSubjectCourses {
	/** The semester of classes */
	Semester semester = null;
	/** The subject of classes */
	Subject subject = null;
	/** The classes matching the semester + subject */
	List<Clazz> classes = new ArrayList<>();

	public SemesterAndSubjectCourses(Semester semester, Subject subject) {
		this.semester = semester;
		this.subject = subject;
	}

	/**
	 * @param clazz
	 *            The {@link Clazz} to remeber for this subject + semester
	 *            instance.
	 */
	public void addClass(Clazz clazz) {
		classes.add(clazz);
	}

	/**
	 * @return The classes registered in this instance.
	 */
	public List<Clazz> getClasses() {
		return classes;
	}
	
	/**
	 * @return The semester registered for this instance.
	 */
	public Semester getSemester()
	{
		return semester;
	}
	
	/**
	 * @return The subject registered for this instance.
	 */
	public Subject getSubject()
	{
		return subject;
	}
}
