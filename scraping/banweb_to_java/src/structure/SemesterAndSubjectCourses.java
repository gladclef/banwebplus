package structure;

import java.util.HashSet;
import java.util.Set;
import java.util.TreeSet;

/**
 * Contains all courses offered for a given subject + semester.
 */
public class SemesterAndSubjectCourses {
	/** The semester of classes */
	Semester semester = null;
	/** The subject of classes */
	Subject subject = null;
	/** The classes matching the semester + subject */
	Set<Clazz> classes = new HashSet<>();

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
	public Set<Clazz> getClasses() {
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
